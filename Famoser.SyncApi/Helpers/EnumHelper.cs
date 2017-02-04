using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Famoser.SyncApi.Enums;

namespace Famoser.SyncApi.Helpers
{
    public class EnumHelper
    {
        public static string EnumToString(SyncAction action)
        {
            switch (action)
            {
                case SyncAction.CreateUser:
                    return "creating user";
                case SyncAction.GetUser:
                    return "getting user";
                case SyncAction.SaveUser:
                    return "saving user";
                case SyncAction.FoundUser:
                    return "found user!";
                case SyncAction.SyncUser:
                    return "syncing user";
                case SyncAction.RemoveUser:
                    return "removing user";

                case SyncAction.GetDevice:
                    return "getting user";
                case SyncAction.CreateDevice:
                    return "creating device";
                case SyncAction.FoundDevice:
                    return "found device";
                case SyncAction.SyncDevice:
                    return "syncing device";
                case SyncAction.SaveDevice:
                    return "saving device";
                case SyncAction.AuthenticateDevice:
                    return "authenticating device";
                case SyncAction.UnAuthenticateDevice:
                    return "unauthenticating device";
                case SyncAction.CreateAuthCode:
                    return "creating auth code";
                case SyncAction.UseAuthCode:
                    return "using auth code";
                case SyncAction.RemoveDevice:
                    return "removing device";

                case SyncAction.GetAllDevices:
                    return "getting all devices";

                case SyncAction.GetCollections:
                    return "getting collections";
                case SyncAction.GetCollectionsAsync:
                    return "getting collections";
                case SyncAction.GetCollectionHistory:
                    return "getting collection history";
                case SyncAction.SyncCollectionHistory:
                    return "syncing collection history";
                case SyncAction.SyncCollection:
                    return "syncing collection";
                case SyncAction.SaveCollection:
                    return "saving collection";
                case SyncAction.RemoveCollection:
                    return "removing collection";
                case SyncAction.AddUserToCollection:
                    return "adding user to collection";

                case SyncAction.GetDefaultCollection:
                    return "getting default collection";

                case SyncAction.GetEntities:
                    return "getting entities";
                case SyncAction.GetEntityHistory:
                    return "getting entity history";
                case SyncAction.SyncEntityHistory:
                    return "syncing entity history";
                case SyncAction.GetEntitiesAsync:
                    return "getting entity";
                case SyncAction.SaveEntity:
                    return "saving entity";
                case SyncAction.RemoveEntity:
                    return "removing entity";
                case SyncAction.SyncEntities:
                    return "syncing entities";

                case SyncAction.CheckAuthentication:
                    return "checking authentication";
                default:
                    return "doing stuff";
            }
        }

        public static string EnumToString(SyncActionError error)
        {
            switch (error)
            {
                case SyncActionError.None:
                    return "none";
                case SyncActionError.ExecutionFailed:
                    return "execution failed";
                case SyncActionError.RequestCreationFailed:
                    return "request creation failed";
                case SyncActionError.RequestUnsuccessful:
                    return "request unsuccessful";
                case SyncActionError.InitializationFailed:
                    return "initialization failed";
                case SyncActionError.WebAccessDenied:
                    return "web access denied";
                case SyncActionError.NotAuthenticatedFully:
                    return "not authenticated fully";
                case SyncActionError.AuthenticationServiceNotSet:
                    return "authentication service not set";
                case SyncActionError.LocalFileAccessFailed:
                    return "local file access failed";
                case SyncActionError.EntityAlreadyRemoved:
                    return "entity already removed";
                default:
                    return "unknown error";
            }
        }
    }
}
