using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Famoser.SyncApi.Models.Base;
using Famoser.SyncApi.Models.Interfaces;

namespace Famoser.SyncApi.Models
{
    public class UserModel : BaseModel, IUserModel
    {
        public override string GetUniqeIdentifier()
        {
            return "user";
        }
    }
}
