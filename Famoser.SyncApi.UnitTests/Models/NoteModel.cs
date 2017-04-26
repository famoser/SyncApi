using System;
using Famoser.SyncApi.Models.Interfaces;
using GalaSoft.MvvmLight;

namespace Famoser.SyncApi.UnitTests.Models
{
    public class NoteModel : ObservableObject, ISyncModel
    {
        public string Content { get; set; }
        private Guid Id { get; set; }

        public Guid GetId()
        {
            return Id;
        }

        public void SetId(Guid id)
        {
            Id = id;
        }

        public string GetClassIdentifier()
        {
            return "notes";
        }
    }
}
